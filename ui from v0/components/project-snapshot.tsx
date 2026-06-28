'use client'

import { makeStyles, tokens, Title3, Body2, Body1 } from '@fluentui/react-components'

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
    paddingBottom: '20px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.08)',
  },
  title: {
    fontSize: '24px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '6px',
    letterSpacing: '-0.3px',
  },
  subtitle: {
    fontSize: '14px',
    color: '#999999',
    fontWeight: 400,
  },
  container: {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
    gap: tokens.spacingHorizontalXL,
  },
  item: {
    display: 'flex',
    flexDirection: 'column',
    gap: tokens.spacingVerticalS,
  },
  label: {
    fontSize: '12px',
    color: '#999999',
    fontWeight: 700,
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
    marginBottom: '8px',
  },
  value: {
    fontSize: '16px',
    fontWeight: 700,
    color: '#333333',
    lineHeight: '1.4',
  },
  progressSection: {
    display: 'flex',
    flexDirection: 'column',
    gap: '20px',
    gridColumn: '1 / -1',
    marginTop: '12px',
    paddingTop: '24px',
    borderTop: '1px solid rgba(0, 0, 0, 0.08)',
  },
  progressLabel: {
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: '12px',
  },
  progressBar: {
    height: '14px',
    backgroundColor: '#EEEEEA',
    borderRadius: '10px',
    overflow: 'hidden',
    boxShadow: 'inset 0 1px 2px rgba(0, 0, 0, 0.04)',
  },
  progressFill: {
    height: '100%',
    width: '72%',
    backgroundColor: '#66BB6A',
    transition: 'width 0.6s ease',
    borderRadius: '10px',
  },
})

export function ProjectSnapshot() {
  const styles = useStyles()

  return (
    <div className={styles.root}>
      <div className={styles.header}>
        <div className={styles.title}>Assigned Project</div>
        <div className={styles.subtitle}>Your current supervision assignment</div>
      </div>
      <div className={styles.container}>
        <div className={styles.item}>
          <div className={styles.label}>Project Name</div>
          <div className={styles.value}>Westfield Plaza Development</div>
        </div>

        <div className={styles.item}>
          <div className={styles.label}>Project Location</div>
          <div className={styles.value}>Downtown District, City</div>
        </div>

        <div className={styles.item}>
          <div className={styles.label}>Current Status</div>
          <div className={styles.value}>Active</div>
        </div>

        <div className={styles.item}>
          <div className={styles.label}>Days Remaining</div>
          <div className={styles.value}>87 Days</div>
        </div>

        <div className={styles.item}>
          <div className={styles.label}>Target Completion</div>
          <div className={styles.value}>Sep 22, 2024</div>
        </div>

        <div className={styles.progressSection}>
          <div className={styles.progressLabel}>
            <span className={styles.label} style={{ margin: 0, marginBottom: 0 }}>Overall Progress</span>
            <span style={{ fontSize: '24px', fontWeight: 700, color: '#2F6B3C' }}>72%</span>
          </div>
          <div className={styles.progressBar}>
            <div className={styles.progressFill} />
          </div>
        </div>
      </div>
    </div>
  )
}
