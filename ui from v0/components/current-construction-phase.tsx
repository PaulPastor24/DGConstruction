'use client'

import { makeStyles, mergeClasses } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '40px',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
    border: '1px solid rgba(0, 0, 0, 0.08)',
  },
  container: {
    display: 'grid',
    gridTemplateColumns: '1fr 1fr 1fr',
    gap: '48px',
    alignItems: 'center',
    '@media (max-width: 1024px)': {
      gridTemplateColumns: '1fr 1fr',
    },
    '@media (max-width: 768px)': {
      gridTemplateColumns: '1fr',
    },
  },
  progressSection: {
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    gap: '24px',
  },
  circularProgress: {
    width: '140px',
    height: '140px',
    borderRadius: '50%',
    background: 'conic-gradient(#66BB6A 0deg, #66BB6A 234deg, #EEEEEA 234deg)',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    boxShadow: '0 2px 8px rgba(102, 187, 106, 0.2)',
  },
  progressValue: {
    fontSize: '48px',
    fontWeight: 700,
    color: '#2F6B3C',
  },
  phaseLabel: {
    fontSize: '12px',
    color: '#999999',
    fontWeight: 700,
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
  },
  phaseName: {
    fontSize: '28px',
    fontWeight: 700,
    color: '#333333',
    marginTop: '12px',
  },
  detailsSection: {
    display: 'flex',
    flexDirection: 'column',
    gap: '20px',
  },
  detailItem: {
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    paddingBottom: '16px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.06)',
    '&:last-child': {
      borderBottom: 'none',
    },
  },
  detailLabel: {
    fontSize: '13px',
    color: '#999999',
    fontWeight: 600,
  },
  detailValue: {
    fontSize: '14px',
    fontWeight: 600,
    color: '#333333',
    textAlign: 'right',
  },
  statusBadge: {
    display: 'inline-flex',
    alignItems: 'center',
    gap: '6px',
    paddingTop: '6px',
    paddingBottom: '6px',
    paddingLeft: '12px',
    paddingRight: '12px',
    borderRadius: '20px',
    fontSize: '12px',
    fontWeight: 700,
    backgroundColor: 'rgba(102, 187, 106, 0.15)',
    color: '#2F6B3C',
  },
  buttonsSection: {
    display: 'flex',
    gap: '16px',
  },
  buttonBase: {
    paddingTop: '13px',
    paddingBottom: '13px',
    paddingLeft: '32px',
    paddingRight: '32px',
    borderRadius: '10px',
    border: 'none',
    cursor: 'pointer',
    fontSize: '15px',
    fontWeight: 700,
    transition: 'all 0.3s ease',
    height: '48px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    boxShadow: '0 2px 4px rgba(0, 0, 0, 0.08)',
  },
  buttonPrimary: {
    backgroundColor: '#2F6B3C',
    color: '#FFFFFF',
    flex: 1,
    '&:hover': {
      backgroundColor: '#1f4620',
      boxShadow: '0 4px 12px rgba(47, 107, 60, 0.3)',
      transform: 'translateY(-2px)',
    },
  },
  buttonSecondary: {
    backgroundColor: '#F0F0E8',
    color: '#2F6B3C',
    flex: 1,
    fontWeight: 700,
    '&:hover': {
      backgroundColor: '#E8E8DE',
      boxShadow: '0 4px 12px rgba(0, 0, 0, 0.12)',
      transform: 'translateY(-2px)',
    },
  },
})

export function CurrentConstructionPhase() {
  const styles = useStyles()

  return (
    <div className={styles.root}>
      <div className={styles.container}>
        {/* Progress Circle */}
        <div className={styles.progressSection}>
          <div className={styles.circularProgress}>
            <div style={{ textAlign: 'center' }}>
              <div className={styles.progressValue}>65%</div>
              <div className={styles.phaseLabel} style={{ marginTop: '8px' }}>Complete</div>
            </div>
          </div>
          <div style={{ textAlign: 'center', width: '100%' }}>
            <div className={styles.phaseLabel}>CURRENT PHASE</div>
            <div className={styles.phaseName}>Roofing</div>
            <div className={styles.statusBadge} style={{ margin: '16px auto 0' }}>
              On Schedule
            </div>
          </div>
        </div>

        {/* Details */}
        <div className={styles.detailsSection}>
          <div className={styles.detailItem}>
            <span className={styles.detailLabel}>Planned Start</span>
            <span className={styles.detailValue}>June 5, 2026</span>
          </div>
          <div className={styles.detailItem}>
            <span className={styles.detailLabel}>Actual Start</span>
            <span className={styles.detailValue}>June 5, 2026</span>
          </div>
          <div className={styles.detailItem}>
            <span className={styles.detailLabel}>Planned Finish</span>
            <span className={styles.detailValue}>July 10, 2026</span>
          </div>
          <div className={styles.detailItem}>
            <span className={styles.detailLabel}>Expected Finish</span>
            <span className={styles.detailValue}>July 10, 2026</span>
          </div>
          <div className={styles.detailItem}>
            <span className={styles.detailLabel}>Workers Assigned</span>
            <span className={styles.detailValue}>24 / 28</span>
          </div>
          <div className={styles.detailItem}>
            <span className={styles.detailLabel}>Delay Risk</span>
            <span className={styles.detailValue} style={{ color: '#66BB6A' }}>Low</span>
          </div>
        </div>

        {/* Buttons */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
          <button className={mergeClasses(styles.buttonBase, styles.buttonPrimary)}>
            Update Progress
          </button>
          <button className={mergeClasses(styles.buttonBase, styles.buttonSecondary)}>
            View Timeline
          </button>
        </div>
      </div>
    </div>
  )
}
