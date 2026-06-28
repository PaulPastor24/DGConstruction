'use client'

import { makeStyles } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '32px',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
    border: '1px solid rgba(0, 0, 0, 0.08)',
  },
  header: {
    marginBottom: '28px',
  },
  title: {
    fontSize: '20px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '6px',
  },
  subtitle: {
    fontSize: '14px',
    color: '#999999',
    fontWeight: 400,
  },
  grid: {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))',
    gap: '24px',
  },
  comparisonCard: {
    backgroundColor: '#F5F5F0',
    borderRadius: '12px',
    padding: '20px',
    border: '1px solid rgba(0, 0, 0, 0.06)',
  },
  cardLabel: {
    fontSize: '12px',
    fontWeight: 700,
    color: '#999999',
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
    marginBottom: '8px',
  },
  cardValue: {
    fontSize: '18px',
    fontWeight: 700,
    color: '#333333',
    marginBottom: '12px',
  },
  varianceContainer: {
    display: 'flex',
    alignItems: 'center',
    gap: '8px',
    fontSize: '13px',
    fontWeight: 600,
  },
  varianceBadge: {
    display: 'inline-flex',
    alignItems: 'center',
    gap: '4px',
    paddingTop: '4px',
    paddingBottom: '4px',
    paddingLeft: '8px',
    paddingRight: '8px',
    borderRadius: '16px',
    fontSize: '12px',
    fontWeight: 700,
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
  },
  varianceOn: {
    backgroundColor: 'rgba(102, 187, 106, 0.15)',
    color: '#2F6B3C',
  },
  varianceAhead: {
    backgroundColor: 'rgba(102, 187, 106, 0.15)',
    color: '#2F6B3C',
  },
  varianceDelayed: {
    backgroundColor: 'rgba(255, 238, 88, 0.2)',
    color: '#CC6600',
  },
})

const comparisons = [
  {
    label: 'Planned Start',
    value: 'Jun 5, 2026',
  },
  {
    label: 'Actual Start',
    value: 'Jun 5, 2026',
    variance: 'On Time',
    type: 'on',
  },
  {
    label: 'Planned Finish',
    value: 'Jul 10, 2026',
  },
  {
    label: 'Expected Finish',
    value: 'Jul 10, 2026',
    variance: 'On Schedule',
    type: 'on',
  },
  {
    label: 'Variance (Days)',
    value: '0 Days',
    variance: 'No Delay',
    type: 'on',
  },
  {
    label: 'Schedule Health',
    value: '100%',
    variance: 'Healthy',
    type: 'on',
  },
]

export function ScheduleComparison() {
  const styles = useStyles()

  return (
    <div className={styles.root}>
      <div className={styles.header}>
        <div className={styles.title}>Schedule Comparison</div>
        <div className={styles.subtitle}>Current phase planned vs actual schedule performance</div>
      </div>

      <div className={styles.grid}>
        {comparisons.map((item) => (
          <div key={item.label} className={styles.comparisonCard}>
            <div className={styles.cardLabel}>{item.label}</div>
            <div className={styles.cardValue}>{item.value}</div>
            {item.variance && (
              <div className={styles.varianceContainer}>
                <div
                  className={`${styles.varianceBadge} ${
                    item.type === 'on' ? styles.varianceOn : item.type === 'ahead' ? styles.varianceAhead : styles.varianceDelayed
                  }`}
                >
                  {item.type === 'on' && '✓'} {item.variance}
                </div>
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  )
}
